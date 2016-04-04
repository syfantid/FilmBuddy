package movies;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.Properties;

/**
 * Class to handle the MySQL Database functionality
 * Created by Sofia on 4/3/2016.
 */
public class MovieStorager {
    private static Connection conn;
    private static String myDriver;
    private static String myUrl;
    private static  String user;
    private static String pass;

    /**
     * Initializes the properties based on properties file given in input directory
     */
    static {
        Properties properties = new Properties();
        try {
            properties.load(new FileInputStream(new File("input\\credentials.properties")));
        } catch (IOException e) {
            e.printStackTrace();
        }

        myDriver = properties.getProperty("driver");
        myUrl = properties.getProperty("url");
        user = properties.getProperty("username");
        pass = properties.getProperty("password");
    }

    /**
     * Inserts a movie into the Database
     * @param m The movie to be inserted
     * @throws SQLException In case a connection is not open or there is a problem with the inserted values
     */
    public static void InsertMovietoDB(Movie m) throws SQLException {
        try {
            Class.forName(myDriver);
            conn = DriverManager.getConnection(myUrl, user, pass);

            // The mysql insert statement
            String query = " insert into all_movies (title, year, categories, synopsis, icon_url, cast, " +
                    "director, imdb_url, extended_plot)" + " values (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Create the mysql insert PreparedStatement
            PreparedStatement preparedStmt = conn.prepareStatement(query);
            preparedStmt.setString(1, m.getTitle());
            preparedStmt.setInt(2, m.getYear());
            preparedStmt.setString(3, m.getCategories());
            preparedStmt.setString(4, m.getSynopsis());
            preparedStmt.setString(5, m.getIconURL());
            preparedStmt.setString(6, m.getCast());
            preparedStmt.setString(7, m.getDirector());
            preparedStmt.setString(8, m.getImdbURL());
            preparedStmt.setString(9, m.getExtendedPlot());

            // Execute the PreparedStatement
            preparedStmt.execute();

            conn.close();
        } catch (Exception e) {
            System.err.println("***************************Got an exception!***************************");
            System.err.println(e.getMessage());
        }
    }
}
