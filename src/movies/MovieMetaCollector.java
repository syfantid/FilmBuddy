package movies;

import com.google.gson.Gson;
import com.mongodb.MongoClient;
import com.mongodb.client.MongoCollection;
import com.mongodb.client.MongoDatabase;
import com.omertron.omdbapi.OMDBException;
import com.omertron.omdbapi.OmdbApi;
import com.omertron.omdbapi.model.OmdbVideoFull;
import com.omertron.omdbapi.tools.OmdbBuilder;
import database.Database;
import org.bson.Document;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Collects meta-information about the movies from OMDb and stores them in MongoDB
 * Created by Sofia on 4/7/2016.
 */
public class MovieMetaCollector {
    // Fields for movies DB
    public static final String _MOVIE_JSON_  = "movie";
    public static final String _MOVIE_ID_ = "movie_id";

    private MongoDatabase _db;
    private String _coll_name_movies;

    /**
     * Connects to the given server:port to the database fixthefixing
     * @param host The host name of the server
     * @param port The port of the server
     */
    public MovieMetaCollector(String host, int port) {
        MongoClient mongoClient = new MongoClient(host, port);
        _db = mongoClient.getDatabase("movies");
        _coll_name_movies = "all_movies";
    }

    /**
     * Inserts movie JSON to movies collection
     * @param id The movie's MySQL DB id
     * @param imdbID The movie's IMDb ID
     */
    public void addMovie(String id, String imdbID) {
        MongoCollection<Document> coll = _db.getCollection(_coll_name_movies);
        String json = findMovie(imdbID);
        
        if(!json.isEmpty()) {
            Document doc = new Document(_MOVIE_JSON_, Document.parse(json))
                    .append(_MOVIE_ID_, id);

            coll.insertOne(doc);
        }
    }

    /**
     * Finds a movie based on its IMDb ID
     * @param imdbID The movie's ID
     * @return The movie's JSON string
     */
    private String findMovie(String imdbID) {
        OmdbApi omdb = new OmdbApi();
        Gson gson = new Gson();
        try {
            // TODO: 4/7/2016 Throws exception; Check with Github developer 
            OmdbVideoFull result = omdb.getInfo(new OmdbBuilder().setImdbId(imdbID).setPlotLong().build());
            return gson.toJson(result);
        } catch (OMDBException e) {
            e.printStackTrace();
        }
        return "";
    }

    /**
     * Connection with MySQL to obtain the IMDB IDs and with Mongo to store metadata from OMDb
     * @param args Main function's arguments; can be null
     */
    public static void main(String[] args) throws SQLException {
        MovieMetaCollector mongoConnector = new MovieMetaCollector("localhost", 27017);
        Database db = new Database();
        if (db.connect()) {
            final Connection conn = db.getConnection();

            // The mysql select statement
            String query = " SELECT id,imdb_url FROM `all_movies` ";

            // Create the mysql select PreparedStatement
            PreparedStatement preparedStmt = conn.prepareStatement(query);

            // Execute the PreparedStatement
            try {
                ResultSet rs = preparedStmt.executeQuery(query); 
                while (rs.next()) { // For each record/row
                    String id = rs.getString("id");
                    String imdbid = rs.getString("imdb_url").replace("http://www.imdb.com/title/","");
                    System.out.println("ID: " + id + " IMDb ID: " + imdbid);
                    if(!imdbid.isEmpty()) {
                        mongoConnector.addMovie(id, imdbid); // Store movie's metadata to MongoDB
                    }
                }
            } catch (Exception e) {
                e.printStackTrace();
            }

            conn.close();
        }
    }
}
